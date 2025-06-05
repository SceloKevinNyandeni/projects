package za.ac.cput.Domain;

import jakarta.persistence.*;

import java.util.List;
@Entity
public class Author {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long authorId;

    private String authorName;


    @ManyToMany(mappedBy = "authors")
    private List<Book> books;

    public Author() {

    }

    public Author(AuthorBuilder authorBuilder){
        this.authorId = authorBuilder.authorId;
        this.authorName = authorBuilder.authorName;
        this.books = authorBuilder.books;
    }

    public Long getAuthorId() {
        return authorId;
    }

    public String getAuthorName() {
        return authorName;
    }

    public List<Book> getBooks() {
        return books;
    }

    @Override
    public String toString() {
        return "Author{" +
                "authorId=" + authorId +
                ", authorName='" + authorName + '\'' +
                ", books=" + books +
                '}';
    }

    public static class AuthorBuilder {
        private Long authorId;
        private String authorName;
        private List<Book> books;

        public AuthorBuilder() {

        }
        public AuthorBuilder setAuthorId(Long authorId) {
            this.authorId = authorId;
            return this;
        }
        public AuthorBuilder setAuthorName(String authorName) {
            this.authorName = authorName;
            return this;
        }
        public AuthorBuilder setBooks(List<Book> books) {
            this.books = books;
            return this;
        }
        public Author build() {
            return new Author(this);
        }
    }
}
